from flask import Blueprint, make_response, jsonify, flash, redirect, url_for, request, current_app

bp = Blueprint('static', __name__)

@bp.route('/')
def index():
    return "Hello, World!"

@bp.route('/index')

@bp.route('/hello')
def hello():
    return 'Welcome to TB API!'

