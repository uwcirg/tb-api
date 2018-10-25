import os
from tbapi.factory import create_app
from flask_sqlalchemy import SQLAlchemy

app = create_app(os.getenv('FLASK_CONFIG') or 'default')
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://tbapi:tbapi@tbapp-db:3306/tbapi'
db = SQLAlchemy(app)

@app.shell_context_processor
def make_shell_context():
    return dict(app=app, db=db)

def init_db():
    app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://tbapi:tbapi@tbapp-db:3306/tbapi'
    db = SQLAlchemy(app)
   
    class AlembicVersion(db.Model):
        __tableName__ = 'alembic_version'
        version_num = db.Column(db.String(32), primary_key=True, nullable=False)

    class OAuth2Client(db.Model):
        __tablename__ = 'oauth2_client'
        
        client_id = db.Column(db.String(48), index=True, nullable=True)
        client_secret = db.Column(db.String(120), nullable=False)
        is_confidential = db.Column(db.Integer, nullable=False)
        redirect_uris = db.Column(db.Text, nullable=False)
        default_redirect_uri = db.Column(db.Text, nullable=False)
        allowed_scopes = db.Column(db.Text)
        id = db.Column(db.Integer, primary_key=True)
        user_id = db.Column(db.Integer, nullable=False)
        name = db.Column(db.String(48), nullable=False)
        website = db.Column(db.Text)
        allowed_grants = db.Column(db.Text)


    class OAuth2AuthorizationCode(db.Model):
        __tablename__ = 'oauth2_code'
        id = db.Column(db.Integer, primary_key=True)
        user_id = db.Column(db.Integer, nullable=False)
        code = db.Column(db.String(120), unique=True, nullable=False)
        client_id = db.Column(db.String(48), nullable=True)
        redirect_uri = db.Column(db.Text)
        scope = db.Column(db.Text)
        expires_at = db.Column(db.Integer, nullable=False)

    class OAuth2Token(db.Model):
        __tablename__ = 'oauth2_token'
        id = db.Column(db.Integer, primary_key=True)
        user_id = db.Column(db.Integer, nullable=False)
        client_id = db.Column(db.String(48), nullable=True)
        token_type = db.Column(db.String(40), nullable=True)
        access_token = db.Column(db.String(255), unique=True, nullable=False)
        refresh_token = db.Column(db.String(255), index=True, nullable=True)
        scope = db.Column(db.Text)
        created_at = db.Column(db.Integer, nullable=False)
        expires_in = db.Column(db.Integer, nullable=False)

    db.create_all()
    db.session.execute("""INSERT INTO `alembic_version` VALUES ('a0a902e9fba9');""")
    db.session.execute("""INSERT INTO `oauth2_client` VALUES ('skwIPnbi7N3uIvNysUbi0xfXwnWaIMR1MCJxz8rV0dGxeMJD','0',0,'https://tb-mobile.cirg.washington.edu/redirect','https://tb-mobile.cirg.washington.edu/redirect','email',1,576,'tb-mobile-app','https://tb-mobile.cirg.washington.edu','implicit');""")
    db.session.execute("""INSERT INTO `oauth2_client` VALUES ('pkwIPnbi7N3uIvNysUbi0xfXwnWaIMR1MCJxz8rV0dGxeMJD','0',0,'https://mpower-dev.cirg.washington.edu/mpower_tb-ivanc/auth/truenth/oauth2callback','https://tb-mobile.cirg.washington.edu/auth/truenth/oauth2callback','email',2,576,'mpower-tb','https://mpower-dev.cirg.washington.edu','authorization_code');""")
    db.session.commit()
    db.session.close()

@app.cli.command('initdb')
def initdb_command():
    app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://tbapi:tbapi@tbapp-db:3306'
    db = SQLAlchemy(app)
    try:
        db.session.execute('CREATE SCHEMA IF NOT EXISTS tbapi')
        db.session.close()
        init_db()
        print('Initialized the database.')
    except:
        db.session.close()
        print('Error creating database.')

    
