FROM nginx:latest
EXPOSE 80
RUN apt-get update && apt-get remove -y nginx && apt-get install -y --no-install-recommends nginx-extras
RUN rm -f /etc/nginx/sites-enabled/default
COPY conf.d/auth.conf /etc/nginx/conf.d/default.conf
