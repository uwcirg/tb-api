from flask import Flask, Blueprint, make_response, jsonify, flash, redirect, url_for, request, current_app
from flask_swagger import swagger
from tbapi.models.mpower import Patient, User, Note
from pathlib import Path

bp = Blueprint('static', __name__)

@bp.route('/')
def index():
    return "Hello, World!"

@bp.route('/index')

@bp.route('/hello')
def hello():
    return 'Welcome to TB API!'

@bp.route('/users')
def user_index():
    return jsonify(users=[i.serialize for i in User.query.all()])

@bp.route('/patients')
def patient_index():
    return jsonify(patients=[i.serialize for i in Patient.query.all()])

@bp.route('api/notes', methods=['GET'])
@bp.route('api/notes/', methods=['GET'])
@bp.route('api/notes/<int:param>', methods=['GET'])
@bp.route('api/notes/<param>', methods=['GET']) # note json
def get(param=None):
    if (param is not None) & isinstance(param, int): # patient_id
        return jsonify(notes=[i.serialize for i in Note.get_by_patient_id(param)])
    elif param: # note
        return jsonify(notes=[i.serialize for i in Note.get_by_note(param)])
    else: # param is none
        return jsonify(notes=[i.serialize for i in Note.query.all()])

@bp.route('api/notes/<noteList>', methods=['POST'])
def post(noteList):
    return jsonify(notes=[i.serialize for i in Note.post(noteList)])

@bp.route('api/notes/<noteList>', methods=['PUT'])
def put(noteList):
    return jsonify(notes=[i.serialize for i in Note.put(noteList)])

# @bp.route('api/sspec')
# def sspec():
#     """generate swagger friendly docs from code and comments
#     View function to generate swagger formatted JSON for API
#     documentation.  Pulls in a few high level values from the
#     package data (see setup.py) and via flask-swagger, makes
#     use of any yaml comment syntax found in application docstrings.
#     Point Swagger-UI to this view for rendering
#     """
#     swag = swagger(current_app)
#     metadata = current_app.config.metadata
#     swag.update({
#         "info": {
#             "version": metadata['version'],
#             "title": metadata['summary'],
#             "termsOfService": metadata['home-page'],
#             "contact": {
#                 "name": metadata['author'],
#                 "email": metadata['author-email'],
#                 "url": metadata['home-page'],
#             },
#         },
#         "schemes": ("http", "https"),
#     })
#     return jsonify(swag)
    

@bp.route('api/spec')
def spec():
    p = Path('swagger.json')
    if p.exists():
        swagger.json = p.read_text()
        return swagger.json
    else:
        return "File not found"        

