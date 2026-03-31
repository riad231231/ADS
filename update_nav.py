import os
import re

def update_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Remove Blog link from nav
    # Pattern: <li class="nav-item"><a href="blog.html" class="nav-link".*?>Blog</a></li>
    content = re.sub(r'<li class="nav-item"><a href="blog\.html".*?>Blog</a></li>\s*', '', content, flags=re.IGNORECASE | re.DOTALL)

    # 2. Add Connexion link to nav if not already there
    if 'id="nav-connexion"' not in content:
        # Find the end of the nav-list
        # Pattern: (<ul class="nav-list">.*?)(</ul>)
        nav_match = re.search(r'(<ul class="nav-list">.*?)(\s*</ul>)', content, flags=re.DOTALL)
        if nav_match:
            new_nav_item = '\n            <li class="nav-item"><a href="login.html" class="nav-link" id="nav-connexion">Connexion</a></li>'
            content = content[:nav_match.end(1)] + new_nav_item + content[nav_match.start(2):]

    # 3. Remove login-widget (handles both versions: with form or just link)
    # Pattern: <section class="widget" id="login-widget">.*?</section>
    content = re.sub(r'<!-- Module Connexion.*?-->\s*<section class="widget" id="login-widget">.*?</section>', '', content, flags=re.DOTALL)
    content = re.sub(r'<!-- Module Connexion -->\s*<section class="widget" id="login-widget">.*?</section>', '', content, flags=re.DOTALL)
    content = re.sub(r'<section class="widget" id="login-widget">.*?</section>', '', content, flags=re.DOTALL)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

# List of HTML files in root
files = [f for f in os.listdir('.') if f.endswith('.html')]

for f in files:
    print(f"Updating {f}...")
    update_file(f)

print("Done.")
