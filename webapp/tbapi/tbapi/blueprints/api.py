from flask import Blueprint, render_template, make_response, jsonify, flash, redirect, url_for, request, current_app
from tbapi.models.mpower import Patient, User

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
