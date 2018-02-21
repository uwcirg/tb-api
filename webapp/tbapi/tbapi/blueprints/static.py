from flask import Blueprint, make_response, jsonify, flash, redirect, url_for, request, current_app

static = Blueprint('static', __name__)

@static.route('/')
def index():
    return "Hello, World!"

@static.route('/index')

@static.route('/hello')
def hello():
    return 'Welcome to TB API!'

