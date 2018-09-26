from flask import Flask, Blueprint, make_response, jsonify, flash, redirect, url_for, request, current_app
from tbapi.models.mpower import Patient, User
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
