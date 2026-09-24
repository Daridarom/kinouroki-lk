#!/usr/bin/env python3
"""Статичная выгрузка Laravel-копии для GitHub Pages.

Запускает `php artisan serve` с KA_STATIC_ROOT, обходит все внутренние ссылки
и сохраняет страницы в ../docs/<путь>/index.html плюс public/css и public/js.

    cd laravel && python3 scripts/export-static.py
"""
import os, re, shutil, subprocess, sys, time, urllib.request
from pathlib import Path
from urllib.parse import urlparse, unquote

ROOT_URL = os.environ.get('KA_STATIC_ROOT', 'https://daridarom.github.io/kinouroki-lk')
PREFIX = urlparse(ROOT_URL).path.rstrip('/')          # /kinouroki-lk
PORT = 8899
APP = Path(__file__).resolve().parent.parent
OUT = APP.parent / 'docs'

env = dict(os.environ, KA_STATIC_ROOT=ROOT_URL)
srv = subprocess.Popen(['php', 'artisan', 'serve', f'--port={PORT}'], cwd=APP, env=env,
                       stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
time.sleep(2)
try:
    if OUT.exists():
        shutil.rmtree(OUT)
    OUT.mkdir()
    for d in ('css', 'js', 'img'):
        shutil.copytree(APP / 'public' / d, OUT / d)
    (OUT / '.nojekyll').write_text('')
    (OUT / 'robots.txt').write_text('User-agent: *\nDisallow: /\n')

    seen, queue = set(), ['/films', '/news', '/users/0']
    link_re = re.compile(r'href="' + re.escape(ROOT_URL) + r'(/[^"#?]*)')
    while queue:
        path = queue.pop()
        if path in seen:
            continue
        seen.add(path)
        try:
            with urllib.request.urlopen(f'http://127.0.0.1:{PORT}{path}') as r:
                html = r.read().decode()
        except Exception as e:
            print('ERR', path, e); continue
        target = OUT / unquote(path).strip('/') / 'index.html'
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(html)
        for p in link_re.findall(html):
            if not p.startswith(('/css/', '/js/', '/img/')) and p not in seen:
                queue.append(p)
    # корень сайта → каталог фильмов
    (OUT / 'index.html').write_text(
        f'<!doctype html><meta charset="utf-8"><meta http-equiv="refresh" content="0; url={ROOT_URL}/films/">'
        f'<a href="{ROOT_URL}/films/">Открыть каталог фильмов</a>')
    print(f'OK: {len(seen)} страниц → {OUT}')
finally:
    srv.terminate()
