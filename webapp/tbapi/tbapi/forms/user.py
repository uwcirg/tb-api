from wtforms.fields import StringField, PasswordField
from wtforms.fields.html5 import EmailField
from wtforms.validators import DataRequired
from wtforms.validators import StopValidation
from .base import BaseForm
from ..models import db
from ..models.mpower import User
from ..auth import login


class AuthenticateForm(BaseForm):
    email = EmailField(description='Correo electrónico', validators=[DataRequired()])
    password = PasswordField(description='Contraseña', validators=[DataRequired()])
 
    def __init__(self, *args, **kwargs):
        super(AuthenticateForm, self).__init__(*args, **kwargs)
        self._user = None

    def validate_password(self, field):
        email = self.email.data.lower()
        user = User.query.filter_by(email=email).first()
        if not user or not user.check_password(field.data):
            raise StopValidation('El correo electrónico que ingresaste no coinciden con ninguna cuenta.')
        self._user = user

    def login(self):
        if self._user:
            login(self._user, True)


class UserCreationForm(BaseForm):
    email = EmailField(description='Correo electrónico', validators=[DataRequired()])
    password = PasswordField(description='Contraseña', validators=[DataRequired()])

    def validate_email(self, field):
        email = field.data.lower()
        user = User.query.filter_by(email=email).first()
        if user:
            raise StopValidation('Este correo electrónico existe.')

    def signup(self):
        email = self.email.data.lower()
        user = User(email=email)
        user.pw = self.password.data
    
        # try:
        #     db.session.add(user)
        #     db.session.commit()
        # except:
        #     db.session.rollback()
        #     raise


        try:
            db.session.add(user)
            db.session.commit()
        except:
            db.session.rollback()
            raise

        login(user, True)
        return user
