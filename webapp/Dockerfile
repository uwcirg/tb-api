FROM python:3.6

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

COPY ./tbapi .
RUN pip install --editable .

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.0.0/wait /wait
RUN chmod +x /wait

CMD /wait && python -m flask run --host=0.0.0.0
