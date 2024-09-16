# Menggunakan base image Nginx resmi dari Docker Hub
FROM nginx:latest

# Copy file konfigurasi Nginx dari host ke container
COPY ./nginx/nginx.conf /etc/nginx/nginx.conf

# Copy file statis (HTML, CSS, JS) ke dalam direktori Nginx default (/usr/share/nginx/html)
COPY ./src /usr/share/nginx/html

# Expose port 80 untuk HTTP
EXPOSE 80

# Perintah default untuk menjalankan Nginx di foreground
CMD ["nginx", "-g", "daemon off;"]