FROM debian:latest
RUN apt-get update && apt-get install -y --no-install-recommends apache2 libapache2-mod-authnz-external curl
COPY default.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite -q 000-default
COPY apache-auth.sh /
RUN chmod a+rx /apache-auth.sh
EXPOSE 80
CMD ["apache2ctl", "-DFOREGROUND"]
