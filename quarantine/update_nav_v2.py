import os
import re

def update_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. CLEANUP: Remove any existing (possibly mis-placed) Connexion nav item
    content = re.sub(r'<li class="nav-item"><a href="login\.html" class="nav-link" id="nav-connexion">Connexion</a></li>\s*', '', content)

    # 2. Add Connexion link to nav correctly
    # We look for the </ul> that is just before </nav>
    nav_end_pattern = re.compile(r'(\s*)</ul>(\s*)</nav>', re.IGNORECASE | re.DOTALL)
    
    def replacer(match):
        new_item = '\n            <li class="nav-item"><a href="login.html" class="nav-link" id="nav-connexion">Connexion</a></li>'
        return new_item + match.group(1) + '</ul>' + match.group(2) + '</nav>'

    content = nav_end_pattern.sub(replacer, content, count=1)

    # 3. Ensure Blog link is removed (already done by previous script, but to be sure)
    content = re.sub(r'<li class="nav-item"><a href="blog\.html".*?>Blog</a></li>\s*', '', content, flags=re.IGNORECASE | re.DOTALL)

    # 4. Remove login-widget (already done by previous script)
    content = re.sub(r'<!-- Module Connexion.*?-->\s*<section class="widget" id="login-widget">.*?</section>', '', content, flags=re.DOTALL)
    content = re.sub(r'<section class="widget" id="login-widget">.*?</section>', '', content, flags=re.DOTALL)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

# List of HTML files
files = [f for f in os.listdir('.') if f.endswith('.html')]

for f in files:
    print(f"Refining {f}...")
    update_file(f)

# Also delete blog.html if it exists
if os.path.exists('blog.html'):
    print("Deleting blog.html...")
    os.remove('blog.html')

print("Done.")
