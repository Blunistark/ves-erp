#!/usr/bin/env python3
"""
CLI Script: Extract inline CSS from student/dashboard PHP files into separate CSS files.
Usage: python extract_css.py
"""
import os
import re

def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    css_dir = os.path.join(script_dir, 'css')
    if not os.path.isdir(css_dir):
        os.makedirs(css_dir)

    processed = []
    skipped = []
    script_name = os.path.basename(__file__)

    for filename in os.listdir(script_dir):
        if not filename.endswith('.php') or filename == script_name:
            continue
        path = os.path.join(script_dir, filename)
        with open(path, 'r', encoding='utf-8') as f:
            content = f.read()
        blocks = re.findall(r'<style\b[^>]*>(.*?)</style>', content, flags=re.S)
        if not blocks:
            print(f'No inline CSS in {filename}, skipped.')
            skipped.append(filename)
            continue
        css_content = '\n\n'.join(block.strip() for block in blocks)
        css_filename = filename[:-4] + '.css'
        css_path = os.path.join(css_dir, css_filename)
        with open(css_path, 'w', encoding='utf-8') as f:
            f.write(css_content)

        # Remove all <style> blocks (fix regex: single backslash)
        new_content = re.sub(r'<style\b[^>]*>.*?</style>', '', content, flags=re.S)
        # Remove old per-page CSS link if present
        link_pattern = rf'<link rel="stylesheet" href="css/{re.escape(css_filename)}">'
        new_content = re.sub(link_pattern, '', new_content)
        # Insert sidebar and page CSS links before </head>
        sidebar_link = '<link rel="stylesheet" href="css/sidebar.css">'
        page_link = f'<link rel="stylesheet" href="css/{css_filename}">' 
        if '</head>' in new_content:
            new_content = new_content.replace('</head>', f'    {sidebar_link}\n    {page_link}\n</head>', 1)
        with open(path, 'w', encoding='utf-8') as f:
            f.write(new_content)

        print(f'Processed {filename} -> css/{css_filename}')
        processed.append(filename)

    print('\nSummary:')
    print(f'Processed: {processed}')
    print(f'Skipped (no inline CSS): {skipped}')

if __name__ == '__main__':
    main()
