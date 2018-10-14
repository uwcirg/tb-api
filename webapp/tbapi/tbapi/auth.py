from flask import session
from .models.mpower import User

def current_user():
    if 'id' in session:
        uid = session['id']
        return User.query.get(uid)
    return None

def logout():
    del session['id']
    return redirect('/')

def login(user, permanent=True):
    session['id'] = user.id
    session.permanent = permanent
    g.current_user = user


# from functools import wraps
# from authlib.flask.client import OAuth
# from flask import g, session
# from flask import url_for, redirect, request
# from werkzeug.local import LocalProxy

# def login(user, permanent=True):
#     session['sid'] = user.id
#     session.permanent = permanent
#     g.current_user = user


# def current_user():
#     if 'id' in session:
#         uid = session['id']
#         return User.query.get(uid)
#     return None





# def get_current_user():
#     user = getattr(g, 'current_user', None)
#     if user:
#         return user

#     sid = session.get('sid')
#     if not sid:
#         return None

#     user = User.query.get(sid)
#     if not user:
#         logout()
#         return None

#     g.current_user = user
#     return user


# current_user = LocalProxy(get_current_user)


# def fetch_token(name):
#     user = get_current_user()
#     conn = Connect.query.filter_by(
#         user_id=user.id, name=name).first()
#     return conn.to_dict()


# def require_login(f):
#     @wraps(f)
#     def decorated(*args, **kwargs):
#         if not current_user:
#             url = url_for('account.login', next=request.path)
#             return redirect(url)
#         return f(*args, **kwargs)
#     return decorated


# oauth = OAuth(fetch_token=fetch_token)

# def init_app(app):
#     oauth.init_app(app)
