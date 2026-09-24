#!/usr/bin/env python3
"""Сборка статичного макета «Киноуроки» из data/*.json в папку docs/.

Запуск:  python3 build.py
Результат: docs/ — готовый сайт (его же отдаёт GitHub Pages).
"""
import json, re, shutil, html
from pathlib import Path
from urllib.parse import quote
from jinja2 import Environment, FileSystemLoader, select_autoescape

ROOT = Path(__file__).parent
OUT = ROOT / "docs"
LIVE = "https://lk.kinouroki.org"

env = Environment(loader=FileSystemLoader(ROOT / "src"), autoescape=select_autoescape(["html"]),
                  trim_blocks=True, lstrip_blocks=True)


def u(path):
    """Абсолютная ссылка на файл боевого сайта с корректным кодированием."""
    return LIVE + quote(path, safe="/:?=&%")


# ---------- Фильмы ----------
SLUGS = {"Отыщи моё сердце": "57"}          # у одного фильма в адресе кириллица с пробелом
STAGES = [("primary", "Начальная школа", "1–4 класс"),
          ("middle", "Основная школа", "5–9 класс"),
          ("senior", "Старшая школа", "10–11 класс")]


def stages_of(text):
    t = (text or "").upper()
    res = []
    if "НАЧАЛЬН" in t:
        res.append("primary")
    if "ОСНОВН" in t or "СРЕДН" in t or re.search(r"\b[5-9]\s*[–-]", t):
        res.append("middle")
    if "СТАРШ" in t or re.search(r"10\s*[–-]\s*11|8\s*[–-]\s*11", t):
        res.append("senior")
    return res


def video_url(v):
    if not v:
        return ""
    kind, _, rest = v.partition(":")
    if kind == "r":
        vid, _, q = rest.partition("?")
        vid = vid.rstrip("/")
        return f"https://rutube.ru/play/embed/{vid}/" + (f"?{q}" if q else "")
    if kind == "y":
        return f"https://www.youtube.com/embed/{rest}"
    if kind == "f":
        return u("/storage/films/triller/" + rest)
    return v


def prep_film(f, order):
    slug = SLUGS.get(f["id"], f["id"])
    paras = [p.strip() for p in f.get("ab", "").split("\n\n") if p.strip()]
    audience = f.get("a", "")
    # первый абзац-«шапка» капсом (аудитория/тема) — уносим в метаданные, а не в текст
    while paras and paras[0].upper() == paras[0] and len(paras[0]) < 160:
        head = paras.pop(0)
        if "ТЕМА" not in head.upper() or stages_of(head):
            audience = audience or head
    audience_clean = re.split(r"[,.]?\s*ТЕМА", audience, flags=re.I)[0].strip(" ,.") if audience else ""
    st = stages_of(audience) or stages_of(" ".join(paras[:1]))
    poster = f["p"] if f["p"].startswith("/") else "/storage/films/poster/" + f["p"]
    photos = [(p if p.startswith("/") else "/storage/films/photos/" + p) for p in f.get("ph", [])]
    links = []
    for l in f.get("l", []):
        if isinstance(l, str):
            links.append(("Фильм и методическое пособие", "https://" + l, "Скачать архив на Яндекс.Диске"))
        else:
            links.append((l[0], "https://" + l[1], "Яндекс.Диск"))
    links.append(("Рекомендации по публикации социальной практики", "https://disk.yandex.ru/i/zL5ztN7NIq-CkQ", "PDF на Яндекс.Диске"))
    if not any("Программа воспитания" in x[0] for x in links):
        links.append(("Программа воспитания «Киноуроки в школах России»", "https://disk.yandex.ru/i/ooUJuKdwzCIWzw", "PDF на Яндекс.Диске"))
    year = (re.search(r"(20\d\d)", f.get("d", "")) or [None, ""])[1]
    return {
        "id": f["id"], "slug": slug, "order": order, "title": f["t"], "quality": f["q"].strip(),
        "date": f.get("d", ""), "year": year, "audience": audience_clean.capitalize() if audience_clean else "",
        "stages": st, "paras": paras, "lead": paras[0] if paras else "",
        "definition": f.get("df", ""), "antipode": f.get("an", ""), "quote": f.get("qt", ""),
        "links": links, "teaser": video_url(f.get("tz", "")), "video": video_url(f.get("v", "")),
        "poster": u(poster), "photos": [u(p) for p in photos], "lesson": f.get("ls", ""),
        "credits": f.get("cr", ""), "live": u("/films/" + f["id"]),
    }


def main():
    films_raw = json.loads((ROOT / "data/films.json").read_text())
    films = [prep_film(f, i) for i, f in enumerate(films_raw)]
    extra = {}
    for name in ("pages", "news"):
        p = ROOT / f"data/{name}.json"
        extra[name] = json.loads(p.read_text()) if p.exists() else {}

    if OUT.exists():
        shutil.rmtree(OUT)
    (OUT / "films").mkdir(parents=True)
    (OUT / "news").mkdir()
    shutil.copytree(ROOT / "assets", OUT / "assets")

    qualities = sorted({f["quality"] for f in films}, key=str.lower)
    common = dict(stages=STAGES, live=LIVE, films_total=len(films))

    def render(tpl, dest, depth, **ctx):
        root = "../" * depth
        (OUT / dest).write_text(env.get_template(tpl).render(root=root, **common, **ctx))

    # компактные данные для фильтров на клиенте
    index_data = [{k: f[k] for k in ("slug", "title", "quality", "year", "stages", "lead", "poster", "order")} for f in films]
    render("films.html", "films/index.html", 1, films=films, qualities=qualities,
           films_json=json.dumps(index_data, ensure_ascii=False), active="films")
    by_q = {}
    for f in films:
        by_q.setdefault(f["quality"], []).append(f)
    for i, f in enumerate(films):
        same_stage = [x for x in films if x is not f and set(x["stages"]) & set(f["stages"])][:4]
        render("film.html", f"films/{f['slug']}.html", 1, f=f, related=same_stage,
               prev=films[i - 1] if i > 0 else None, next=films[i + 1] if i + 1 < len(films) else None,
               active="films")

    news = extra["news"] or []
    pages = extra["pages"] or {}
    render("index.html", "index.html", 0, films=films[:8], news=news[:6], pages=pages, active="home")
    render("news.html", "news/index.html", 1, news=news, active="news")
    for n in news:
        render("news_item.html", f"news/{n['slug']}.html", 1, n=n, latest=[x for x in news if x is not n][:3], active="news")
    for key, pg in pages.items():
        render("page.html", f"{key}.html", 0, page=pg, key=key, active=key)
    print(f"OK: {len(films)} фильмов, {len(news)} новостей, {len(pages)} страниц → {OUT}")


if __name__ == "__main__":
    main()
