from flask import Blueprint, request
from flask import jsonify, render_template
from authlib.specs.rfc6749 import OAuth2Error
from ..models import OAuth2Client
from ..auth import current_user
from ..forms.auth import ConfirmForm, LoginConfirmForm
from ..services.oauth2 import authorization, scopes, require_oauth


bp = Blueprint('oauth2', __name__)


@bp.route('/authorize', methods=['GET', 'POST'])
def authorize(): 
    user = current_user()

    if current_user:
        form = None
    else: 
        form = LoginConfirmForm()


    if request.method == 'GET':
        try:
            grant = authorization.validate_consent_request(end_user=user)
        except OAuth2Error as error:
            payload = dict(error.get_body())
            return jsonify(payload), error.status_code
        
        client = OAuth2Client.get_by_client_id(request.args['client_id'])

        return render_template(
            'account/authorize.html',
            grant=grant,
            scopes=scopes,
            client=client,
            form=form,
        )

    if (form and form.validate_on_submit()) or current_user:
        grant_user = current_user
    else:
        grant_user = None

    return authorization.create_authorization_response(grant_user=grant_user)

@bp.route('/token', methods=['POST'])
def issue_token():
    return authorization.create_token_response()


@bp.route('/revoke', methods=['POST'])
def revoke_token():
    return authorization.create_endpoint_response('revocation')

@bp.route('/me')
@require_oauth('profile')
def api_me():
    user = current_token.user
    return jsonify(id=user.id, username=user.username)