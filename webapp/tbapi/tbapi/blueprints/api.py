from flask import Flask, Blueprint, render_template, make_response, jsonify, flash, redirect, url_for, request, current_app
from tbapi.models.mpower import Patient, User
from flask_swagger import swagger
from tbapi.models.tbapp import Note

bp = Blueprint('api', __name__)

@bp.route('/notes/<int:id>', methods=['GET']) # note id
@bp.route('/notes/', methods=['GET']) # patient_id
def get(id=None):
    if (id is not None) & isinstance(id, int): # note id
      return jsonify(notes=[i.serialize for i in Note.get_by_note(id)])
  
    try:
        patient_id = int(request.args.get('patient_id'))
    except:
        return jsonify(notes=[i.serialize for i in Note.get_by_patient_id(0)])
  
    if (patient_id is not None) & isinstance(patient_id, int): # patient_id
        return jsonify(notes=[i.serialize for i in Note.get_by_patient_id(patient_id)])
    else: # param is none
        return jsonify(notes=[i.serialize for i in Note.get_by_patient_id(0)])

    """Access basics for patient notes

    1. GET /api/notes/?patient_id=int  returns all notes by specified patient_id
    2. GET /api/notes/{int:id} returns note specified by id.

    returns patient notes id, patient_id, text, author_id, created, lastmod in JSON
    ---
    tags:
        - Note GET
    operationId: getNotes
    parameters:
      - in: body
        name: body
        schema:
          id: note
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
    produces:
        - application/json
    responses:
        200:
          description:
              "Returns {notes}"
    """
    
@bp.route('/notes', methods=['POST'])
def post():
    """Create or update a note

    1. POST /api/notes returns the inserted or updated note id.
    ---
    tags:
      - Note POST
    operationId: putNote
    parameters:
      - in: body
        name: body
        schema:
          id: note
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
    produces:
      - application/json
    responses:
      200:
        description:
            "Returns {notes}"
    """
    return jsonify(Note.post(request.get_json()))

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
                "url": "http://tb-mobile/api/v1.0/spec",
            },
        },
        "schemes": ("http", "https"),
    })
    return jsonify(swag)
