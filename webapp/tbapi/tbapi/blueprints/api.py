from flask import Flask, Blueprint, render_template, make_response, jsonify, flash, redirect, url_for, request, current_app
from tbapi.models.mpower import Patient, User
from flask_swagger import swagger
from tbapi.models.tbapp import Note

bp = Blueprint('api', __name__)

@bp.route('/patients')
def patient_index():
    return jsonify(patients=[i.serialize for i in Patienst.query.all()])

@bp.route('/patients/<int:id>')
def patient(id):
    return jsonify(Patient.query.filter_by(id=id).first().serialize)

@bp.route('/users')
def user_index():
    return jsonify(users=[i.serialize for i in User.query.all()])

@bp.route('/users/<int:id>')
def user(id):
    return jsonify(User.query.filter_by(id=id).first().serialize)

@bp.route('/notes', methods=['GET'])
@bp.route('/notes/', methods=['GET'])
@bp.route('/notes/<int:param>', methods=['GET'])
@bp.route('/notes/<param>', methods=['GET']) # note json
def get(param=None):
    """Access basics for patient notes

    1. GET /api/notes returns all notes.
    2. GET /api/notes/ returns all notes.
    3. GET /api/notes/{int:patient_id} returns all notes by patient_id.
    4. GET /api/notes/{json: note} returns the specified note.

    returns patient notes id, patient_id, text, author_id, created, lastmod, flagged and flag_type in JSON
    ---
    tags:
      - Note
    operationId: getNotes
    parameters:
      - in: body
        name: body
        schema:
          id: note_args
          properties:
            consents:
              type: array
              items:
                type: object
                required:
                  - id
                  - patient_id
                  - text
                  - author_id
                  - created
                  - lastmod
                  - flagged
                  - flag_type
                properties:
                  id:
                    type: integer
                    format: int64
                    description: id for note
                  patient_id:
                    type: integer
                    description: patient_id
                  text:
                    type: string
                    description: body of note
                  author_id:
                    type: integer
                    description: author_id
                  created:
                    type: string
                    format: date-time
                    description: created
                  lastmod:
                    type: string
                    format: date-time
                    description: lastmod
                  flagged:
                    type: integer
                    description: flagged 0 or 1
                  flag_type:
                    type: string
                    description: flag_type Enum 'Identifiers in note','Participant distress','Participant feedback','Provider feedback','Technical issue','Data integrity','Report to IRB'
    produces:
      - application/json
    responses:
      200:
        description:
            "Returns {notes}"
    """
    if (param is not None) & isinstance(param, int): # patient_id
        return jsonify(notes=[i.serialize for i in Note.get_by_patient_id(param)])
    elif param: # note
        return jsonify(notes=[i.serialize for i in Note.get_by_note(param)])
    else: # param is none
        return jsonify(notes=[i.serialize for i in Note.query.all()])

@bp.route('/notes/<noteList>', methods=['POST'])
def post(noteList):
    return jsonify(notes=[i.serialize for i in Note.post(noteList)])

@bp.route('/notes/<noteList>', methods=['PUT'])
def put(noteList):
    """Create or update a note

    1. POST /api/notes/{json: note} returns the specified note.
    2. PUT /api/notes/{json: note} returns the specified note.
    ---
    tags:
      - Note
    operationId: putNote
    parameters:
      - in: body
        name: body
        schema:
          id: note_args
          properties:
            consents:
              type: array
              items:
                type: object
                required:
                  - organization_id
                  - agreement_url
                properties:
                  id:
                    type: integer
                    format: int64
                    description: id for note
                  patient_id:
                    type: integer
                    description: patient_id
                  text:
                    type: string
                    description: body of note
                  author_id:
                    type: integer
                    description: author_id
                  created:
                    type: string
                    format: date-time
                    description: created
                  lastmod:
                    type: string
                    format: date-time
                    description: lastmod
                  flagged:
                    type: integer
                    description: flagged 0 or 1
                  flag_type:
                    type: string
                    description: flag_type Enum 'Identifiers in note','Participant distress','Participant feedback','Provider feedback','Technical issue','Data integrity','Report to IRB'
    produces:
      - application/json
    responses:
      200:
        description:
            "Returns {notes}"
    """
    return jsonify(notes=[i.serialize for i in Note.put(noteList)])

@bp.route('/spec')
def spec():
    """generate swagger friendly docs from code and comments
    View function to generate swagger formatted JSON for API
    documentation.  Pulls in a few high level values from the
    package data (see setup.py) and via flask-swagger, makes
    use of any yaml comment syntax found in application docstrings.
    Point Swagger-UI to this view for rendering
    """
    swag = swagger(current_app)
    swag.update({
        "info": {
            "version": "0.0.1",
            "title": "/api/notes",
            "termsOfService": "termsOfService",
            "contact": {
                "name": "name",
                "email": "cirg@uw.edu",
                "url": "url",
            },
        },
        "schemes": ("http", "https"),
    })
    return jsonify(swag)
