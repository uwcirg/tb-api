from flask import Blueprint, render_template, make_response, jsonify, flash, redirect, url_for, request, current_app
from tbapi.models import *

bp = Blueprint('oauth', __name__)

# TODO: Ensure Secure @secure_required
@bp.route('/authorize', methods=["GET", "POST"])
def authorize(*args, **kwargs):
    if request.method == 'GET':
        client_id = kwargs.get('client_id')
        client = Client.query.filter_by(client_id=client_id).first()
        kwargs['client'] = client
        return render_template('oauthorize.html', **kwargs)
    
    if request.method == 'HEAD':
        # if HEAD is supported properly, request parameters like
        # client_id should be validated the same way as for 'GET'
        response = make_response('', 200)
        response.headers['X-Client-ID'] = kwargs.get('client_id')
        return response

    confirm = request.form.get('confirm', 'no')
    return confirm == 'yes'

@bp.route('/oauth/token', methods=['POST', 'GET'])
def access_token():
    return {}

@bp.route('/oauth/revoke', methods=['POST'])
def revoke_token(): pass



# app.post('/api/login', (req, res) => {
#   setTimeout(() => (
#     res.json({
#       success: true,
#       token: API_TOKEN,
#     })
#   ), FAKE_DELAY);
# });