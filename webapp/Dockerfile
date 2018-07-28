FROM python:3.6

# Create the group and user to be used in this container
RUN groupadd flaskgroup && useradd -m -g flaskgroup -s /bin/bash flask

# # Create the working directory (and set it as the working directory)
RUN mkdir -p /home/flask/app/web
WORKDIR /home/flask/app/web


COPY . /home/flask/app/web
RUN pip install --editable .

RUN chown -R flask:flaskgroup /home/flask

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.0.0/wait /wait
RUN chmod +x /wait

USER flask


# CMD /wait && python -m flask run --host=0.0.0.0

# # Install the package dependencies (this step is separated
# # from copying all the source code to avoid having to
# # re-install all python packages defined in requirements.txt
# # whenever any source code change is made)
# COPY requirements.txt /home/flask/app/web
# RUN pip install --no-cache-dir -r requirements.txt

# # Copy the source code into the container
# COPY . /home/flask/app/web



