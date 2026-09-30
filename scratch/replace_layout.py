import os
import re

php_path = 'views/web/product_detail.php'
html_path = 'scratch/new_layout.html'

with open(html_path, 'r', encoding='utf-8') as f:
    new_layout = f.read()

with open(php_path, 'r', encoding='utf-8') as f:
    php_content = f.read()

# Pattern to find everything from <!-- MAIN PRODUCT LAYOUT --> down to <!-- LIGHTBOX MODAL -->
pattern = re.compile(
    r'(<!-- ============================================================ -->\s*<!-- MAIN PRODUCT LAYOUT -->\s*<!-- ============================================================ -->).*?(<!-- ============================================================ -->\s*<!-- LIGHTBOX MODAL -->)',
    re.DOTALL
)

replaced_content = pattern.sub(rf'\1\n{new_layout}\n\2', php_content)

with open(php_path, 'w', encoding='utf-8') as f:
    f.write(replaced_content)

print("Replacement successful!")
