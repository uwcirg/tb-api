import time
from sqlalchemy import Column
from sqlalchemy import (
    Integer, String, Text
)
from authlib.flask.oauth2.sqla import (
    OAuth2ClientMixin,
    OAuth2AuthorizationCodeMixin,
    OAuth2TokenMixin,
)
 
from .base import db, SerializeMixin

db.metadata.clear()

class OAuth2Client(db.Model, OAuth2ClientMixin):
    __tablename__ = 'oauth2_client'

    client_id = db.Column(db.String(48), index=True, nullable=True)
    client_secret = db.Column(db.String(120), nullable=False)
    is_confidential = db.Column(db.Integer(), nullable=False)
    redirect_uris = db.Column(db.Text, nullable=False)
    default_redirect_uri = db.Column(db.Text, nullable=False)
    allowed_scopes = db.Column(db.Text, nullable=False)
    id = db.Column(db.Integer(), primary_key=True, nullable=False)
    user_id = db.Column(db.Integer(), nullable=False)
    name = db.Column(db.String(48), nullable=False)
    website = db.Column(db.Text, nullable=True)
    allowed_grants = db.Column(db.Text, nullable=True)
    

    def check_response_type(self, response_type):
        grant_maps = {'code': 'authorization_code', 'token': 'implicit'}
        grant_type = grant_maps.get(response_type)
        if not grant_type:
            return False
        return self.check_grant_type(grant_type)

    def check_grant_type(self, grant_type):
        return grant_type in self.allowed_grants.split()

    def has_client_secret(self):
        return False

STATIC_OAUTH2_CLIENTS = {
    'skwIPnbi7N3uIvNysUbi0xfXwnWaIMR1MCJxz8rV0dGxeMJD':
        {
            'client_secret': '0',
            'is_confidential': '0',
            'redirect_uris': 'https://tb-mobile.cirg.washington.edu/redirect',
            'default_redirect_uri': 'https://tb-mobile.cirg.washington.edu/redirect',
            'allowed_scopes': 'email',
            'id': '1',
            'user_id': '576',
            'name': 'tb-mobile-app',
            'website': 'https://tb-mobile.cirg.washington.edu',
            'allowed_grants': 'implicit',
        },   
    'pkwIPnbi7N3uIvNysUbi0xfXwnWaIMR1MCJxz8rV0dGxeMJD':
        {
            'client_secret': '0',
            'is_confidential': '0',
            'redirect_uris': 'https://mpower-dev.cirg.washington.edu/mpower_tb-ivanc/auth/truenth/oauth2callback',
            'default_redirect_uri': 'https://tb-mobile.cirg.washington.edu/auth/truenth/oauth2callback',
            'allowed_scopes': 'email',
            'id': '2',
            'user_id': '576',
            'name': 'mpower-tb',
            'website': 'https://mpower-dev.cirg.washington.edu',
            'allowed_grants': 'authorization_code',

        },
}

def add_static_oauth2_clients(db):
    for x in STATIC_OAUTH2_CLIENTS:
        if not OAuth2Client.query.filter_by(client_id=x).first():
            try:
                db.session.add(OAuth2Client(
                    client_id=x, 
                    client_secret=STATIC_OAUTH2_CLIENTS[x]['client_secret'],
                    is_confidential=STATIC_OAUTH2_CLIENTS[x]['is_confidential'],
                    redirect_uris=STATIC_OAUTH2_CLIENTS[x]['redirect_uris'],
                    default_redirect_uri=STATIC_OAUTH2_CLIENTS[x]['default_redirect_uri'],
                    allowed_scopes=STATIC_OAUTH2_CLIENTS[x]['allowed_scopes'],
                    id=STATIC_OAUTH2_CLIENTS[x]['id'],
                    user_id=STATIC_OAUTH2_CLIENTS[x]['user_id'],
                    name=STATIC_OAUTH2_CLIENTS[x]['name'],
                    website=STATIC_OAUTH2_CLIENTS[x]['website'],
                    allowed_grants=STATIC_OAUTH2_CLIENTS[x]['allowed_grants'],
                ))

                
            except Exception as e:
                print ('except:', e)
        
class OAuth2AuthorizationCode(db.Model, OAuth2AuthorizationCodeMixin):
    __tablename__ = 'oauth2_code'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, nullable=False)


class OAuth2Token(db.Model, OAuth2TokenMixin):
    __tablename__ = 'oauth2_token'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, nullable=False)

    def is_refresh_token_expired(self):
        expired_at = self.created_at + self.expires_in * 2
        return expired_at < time.time()

    @classmethod
    def query_token(cls, access_token):
        return cls.query.filter_by(access_token=access_token).first()
