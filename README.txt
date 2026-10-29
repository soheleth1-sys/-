SHADOW HOST — starter files

Files:
- index.html: upload/deploy dashboard
- deploy.php: PHP upload endpoint

Setup:
1. Use a PHP-enabled web host.
2. Upload both files into the same web folder (often public_html or htdocs).
3. Ensure PHP can create/write the "sites" folder in that directory.
4. Open index.html through your hosted domain (not as a local file).
5. Enter a unique website name, select an HTML file, and tap Deploy Website.

Important:
- This starter supports one HTML file only. Linked CSS, JS, images, and other assets are not uploaded.
- It is a prototype, not a production-ready multi-user hosting service.
- Do not expose arbitrary uploaded HTML on the same origin as an admin/login dashboard. Production hosting should isolate user sites on separate origins/subdomains and add authentication, quotas, abuse controls, and secure deployment handling.
- The generated URL will only work if the server allows public access to the /sites/ directory.
