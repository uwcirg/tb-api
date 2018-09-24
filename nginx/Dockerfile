FROM nginx:1.11.3
RUN rm /etc/nginx/nginx.conf
COPY nginx.conf /etc/nginx/
RUN rm /etc/nginx/conf.d/default.conf
COPY tb-api.conf.template /etc/nginx/conf.d/

CMD /bin/bash -c \
    "envsubst '\${TB_API_INTERNAL_PORT}'  < /etc/nginx/conf.d/tb-api.conf.template > /etc/nginx/conf.d/tb-api.conf && exec nginx -g 'daemon off;'"
