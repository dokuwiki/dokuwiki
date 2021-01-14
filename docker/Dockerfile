FROM php:7.4-apache

ARG BRANCH=stable

COPY ./entrypoint.sh /usr/local/sbin/entrypoint
RUN chmod +x /usr/local/sbin/entrypoint

# prepare overlay directories
# https://stackoverflow.com/a/33275168/172068
RUN ["/bin/bash", "-c", "mkdir -p /overlay/{original,storage/{dokuwiki,work}}"]
VOLUME /overlay/storage

# setup DokuWiki
RUN cd /overlay/original && \
    curl -L http://github.com/splitbrain/dokuwiki/tarball/$BRANCH | \
    tar --strip-components=1 -xzvf - && \
    chown -R www-data:www-data .

EXPOSE 80 443
ENTRYPOINT ["entrypoint"]
