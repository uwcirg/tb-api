import os
from flask import Flask, g
from flask_cors import CORS
from flask_migrate import Migrate
from .models import db
from flask_sqlalchemy import SQLAlchemy
import logging

migrate = Migrate()


def create_app(dev=False):
    app = Flask('tbapi')

    app.config.update(dict(
        Debug=True,
        SQLALCHEMY_DATABASE_URI = 'mysql+pymysql://tbapi:tbapi@tbapp-db:3306/tbapi',
        SQLALCHEMY_TRACK_MODIFICATIONS = False,
        SQLALCHEMY_POOL_RECYCLE = 60,
        SQLALCHEMY_BINDS = {
            'mpower':        'mysql+pymysql://mpower:mpower@mpower-db:3306/mpower_demo',
            'tbapi':         'mysql+pymysql://tbapi:tbapi@tbapp-db:3306/tbapi'
        }
    ))

    if dev:
        app.config['DEBUG'] = True
    else:
        logger = logging.getLogger(__name__)
        app.logger.addHandler(logging.StreamHandler())
        app.logger.setLevel(logging.DEBUG)  

    CORS(app)

    app.config.from_envvar('TBAPI_SETTINGS', silent=True)
    app.secret_key = '12345678'

    ## App Creation
    db.init_app(app)

    # Reflect only the structure of the mPOWEr db.
    with app.app_context():
        db.reflect(bind=['mpower','tbapi'])

    from .models import OAuth2Client, OAuth2AuthorizationCode, OAuth2Token        
    from .services import oauth2
    from . import auth, blueprints

    migrate.init_app(app, db)

    #auth.init_app(app)
    oauth2.init_app(app)
    blueprints.init_app(app)

    return app
