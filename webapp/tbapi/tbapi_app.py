import os
from tbapi.factory import create_app
import alembic.config
from flask_migrate import Migrate
from flask_sqlalchemy import SQLAlchemy
from tbapi.models.oauth2 import OAuth2Client, add_static_oauth2_clients

app = create_app(os.getenv('FLASK_CONFIG') or 'default')
MIGRATIONS_DIR = os.path.join(app.root_path, 'migrations')

app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://tbapi:tbapi@tbapp-db:3306/tbapi'
db = SQLAlchemy(app)

@app.shell_context_processor
def make_shell_context():
    return dict(app=app, db=db)

def init_db():
    app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://tbapi:tbapi@tbapp-db:3306/tbapi'
    db = SQLAlchemy(app)
    # I don't beleive this is necessary. Tables are created using migrate (alembic)
    db.create_all()
    
    

@app.cli.command('sync')
def initdb_command():

    app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://tbapi:tbapi@tbapp-db:3306'
    db = SQLAlchemy(app)
    try:
        db.session.execute('CREATE SCHEMA IF NOT EXISTS tbapi')
        db.session.close()
        init_db()
    except Exception as e:
        db.session.close()
        print('Error creating database: ' + str(e))
        exit(0)

    try:
        migrate = Migrate(app, db, directory=MIGRATIONS_DIR)
        upgrade_db()
        stamp_db()
        seed()        
    except Exception as e:
        print('Migrate error: ' + str(e))

def _run_alembic_command(args):
    """Helper to manage working directory and run given alembic commands"""
    # Alembic looks for the alembic.ini file in CWD
    # hop over there and then return to CWD
    cwd = os.getcwd()
    os.chdir(MIGRATIONS_DIR)
    alembic.config.main(argv=args)
    os.chdir(cwd)  # restore cwd

def upgrade_db():
    """Run any outstanding migration scripts"""
    _run_alembic_command(['--raiseerr', 'upgrade', 'head'])

def stamp_db():
    """Run the alembic command to stamp the db with the current head"""
    # if the alembic_version table exists, this db has been stamped,
    # don't update to head, as it would potentially skip steps.
    if db.engine.dialect.has_table(db.engine.connect(), 'alembic_version'):
        return

    _run_alembic_command(['--raiseerr', 'stamp', 'head']) 

@app.cli.command(name="seed")
def seed_command():
    """Seed database with required data"""
    seed()       

def seed():
    """Actual seed function
    NB this is defined separately so it can also be called internally,
    i.e. from sync
    """
    db = SQLAlchemy(app)
    add_static_oauth2_clients(db)
    db.session.commit()
   