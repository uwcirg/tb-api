from authlib.flask.oauth2 import AuthorizationServer, ResourceProtector

from authlib.flask.oauth2.sqla import (
    create_query_client_func,
    create_save_token_func,
    create_revocation_endpoint,
    create_bearer_token_validator
)

from authlib.specs.rfc6749 import grants

from authlib.specs.rfc7009 import RevocationEndpoint as _RevocationEndpoint
from werkzeug.security import gen_salt

from ..models import (
    db,
    OAuth2Client,
    OAuth2AuthorizationCode,
    OAuth2Token,
)

from ..models.mpower import User


class AuthorizationCodeGrant(grants.AuthorizationCodeGrant):
    # Generates Authorization Code
    def create_authorization_code(self, client, user, request):
        code = gen_salt(48)
        item = OAuth2AuthorizationCode(
            code=code,
            client_id=client.client_id,
            redirect_uri=request.redirect_uri,
            scope=request.scope,
            user_id=user.id,
        )
        db.session.add(item)
        db.session.commit()
        return code

    # Parses the authorization code
    def parse_authorization_code(self, code, client):
        item = OAuth2AuthorizationCode.query.filter_by(
            code=code, client_id=client.client_id).first()
        if item and not item.is_expired():
            return item

    def delete_authorization_code(self, authorization_code):
        db.session.delete(authorization_code)
        db.session.commit()
    
    def authenticate_user(self, authorization_code):
        return User.query.get(authorization_code.user_id)
    
    # def create_access_token(self, token, client, authorization_code):
    #     item = OAuth2Token(
    #         client_id=client.client_id,
    #         user_id=authorization_code.user_id,
    #         **token
    #     )
    #     db.session.add(item)
    #     db.session.commit()
    #     token['user_id'] = authorization_code.user_id


#class ImplicitGrant(grants.ImplicitGrant):
# https://github.com/lepture/authlib/blob/master/authlib/specs/rfc6749/grants/implicit.py
    # def create_access_token(self, token, client, grant_user):
    #     item = OAuth2Token(
    #         client_id=client.client_id,
    #         user_id=grant_user.id,
    #         **token
    #     )
    #     db.session.add(item)
    #     db.session.commit()


class PasswordGrant(grants.ResourceOwnerPasswordCredentialsGrant):
    def authenticate_user(self, username, password):
        user = User.query.filter_by(username=username).first()
        if user.check_password(password):
            return user

    # def create_access_token(self, token, client, user):
    #     item = OAuth2Token(
    #         client_id=client.client_id,
    #         user_id=user.id,
    #         **token
    #     )
    #     db.session.add(item)
    #     db.session.commit()
    #     token['user_id'] = user.id


class ClientCredentialsGrant(grants.ClientCredentialsGrant):
    def create_access_token(self, token, client):
        item = OAuth2Token(
            client_id=client.client_id,
            user_id=client.user_id,
            **token
        )
        db.session.add(item)
        db.session.commit()


class RefreshTokenGrant(grants.RefreshTokenGrant):
    def authenticate_refresh_token(self, refresh_token):
        item = OAuth2Token.query.filter_by(refresh_token=refresh_token).first()
        if item and not item.is_refresh_token_expired():
            return item
    
    def authenticate_user(self, credential):
        return User.query.get(credential.user_id)

query_client = create_query_client_func(db.session, OAuth2Client)
save_token = create_save_token_func(db.session, OAuth2Token)
authorization = AuthorizationServer(
    query_client=query_client,
    save_token=save_token,
)

require_oauth = ResourceProtector()

# scopes definition
scopes = {
    'email': 'Access to your email address.',
    'connects': 'Access to your connected networks.'
}



def config_oauth(app):
    authorization.init_app(app)

    # support all grants
    authorization.register_grant(grants.ImplicitGrant)
    authorization.register_grant(grants.ClientCredentialsGrant)
    authorization.register_grant(AuthorizationCodeGrant)
    authorization.register_grant(PasswordGrant)
    authorization.register_grant(RefreshTokenGrant)

    # support revocation
    revocation_cls = create_revocation_endpoint(db.session, OAuth2Token)
    authorization.register_endpoint(revocation_cls)


    # protect resource
    bearer_cls = create_bearer_token_validator(db.session, OAuth2Token)
    require_oauth.register_token_validator(bearer_cls())

# def init_app(app):
#     authorization.init_app(app)
