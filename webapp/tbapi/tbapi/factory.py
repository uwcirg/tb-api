import os
from flask import Flask, g
from flask_cors import CORS
from flask_oauthlib.contrib.oauth2 import bind_sqlalchemy
from .oauth2 import oauth2
from .server import current_user, User, Client, Grant, Token


def create_app(config=None):
    app = Flask('tbapi')

    app.config.update(dict(
        Debug=True,
        SQLALCHEMY_DATABASE_URI = 'mysql+pymysql://tbapi:tbapi@app-db:3306/tbapi',
        SQLALCHEMY_TRACK_MODIFICATIONS = False,
        SQLALCHEMY_POOL_RECYCLE = 60,
        SQLALCHEMY_BINDS = {
            'mpower':        'mysql+pymysql://mpower:mpower@mpower-db:3306/mpower_demo',
        }
    ))

    CORS(app)

    #app.config.update(config or {})
    app.config.from_envvar('TBAPI_SETTINGS', silent=True)

    app.secret_key = '12345678'

    from tbapi.db import db
    db.init_app(app)

    from tbapi.bcrypt import bcrypt
    bcrypt.init_app(app)

    # Reflect only the structure of the mPOWEr db.
    with app.app_context():
        db.reflect(bind='mpower')

    from tbapi.models import Patient, MpowerUser

    from tbapi.blueprints.api import api
    from tbapi.blueprints.static import static
    from tbapi.blueprints.oauth import oauth

    app.register_blueprint(api, url_prefix='/api/v1.0')
    app.register_blueprint(static, url_prefix='')
    app.register_blueprint(oauth, url_prefix='/oauth')

    oauth2.init_app(app)
    bind_sqlalchemy(oauth2, db.session, user=User, token=Token,
                        client=Client, grant=Grant, current_user=current_user)


    # register_cli(app)
    # register_teardowns(app)


    # @app.before_request
    # def load_current_user():
    #     user = User.query.get(1)
    #     g.user = user

    return app
