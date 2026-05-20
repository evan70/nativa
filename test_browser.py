import sys
from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path='/usr/bin/chromium')
        page = browser.new_page()
        page.goto('http://localhost:8000/articles?q=php')
        print(page.title())
        browser.close()

if __name__ == '__main__':
    run()
