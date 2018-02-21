# from flask import Flask
# import os
#
# app = Flask(__name__) #, instance_relative_config=True)
#
# # if isfile(join('instance', 'flask_full.cfg')):
# #     app.config.from_pyfile('flask_full.cfg')
# # else:
# #     app.config.from_pyfile('flask.cfg')
#
# # Config
# # app.config['DEBUG'] = True
# # app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://mpower:mpower@db/tb-api'
# # app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
# # app.config['BASEDIR'] = os.path.abspath(os.path.dirname(__file__))
# app.config.from_object('config')
#
# app.secret_key = '12345678'
#
# from app import routes, models, views
