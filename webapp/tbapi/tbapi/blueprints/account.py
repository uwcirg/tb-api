from flask import Blueprint, jsonify
from flask import url_for, redirect, render_template
from ..auth import current_user, logout as _logout
from ..forms.user import AuthenticateForm, UserCreationForm

bp = Blueprint('account', __name__)


@bp.route('/login', methods=['GET', 'POST'])
def login():
    if current_user:
        return redirect(url_for('static.hello'))
    form = AuthenticateForm()
    if form.validate_on_submit():
        form.login()
        return redirect(url_for('static.hello'))
    return render_template('account/login.html', form=form)


@bp.route('/logout')
def logout():
    _logout() 
    return redirect(url_for('account.login'))


@bp.route('/signup', methods=['GET', 'POST'])
def signup():
    if current_user:
        return redirect("https://tb-mobile.cirg.washington.edu/")
    form = UserCreationForm()
    if form.validate_on_submit():
        form.signup()
        return redirect("https://tb-mobile.cirg.washington.edu/")
    return render_template('account/signup.html', form=form)

@bp.route('/myaccount', methods=['GET'])
def myaccount():
    return jsonify(current_user.serialize)
