from wtforms.fields import (
    PasswordField,
    BooleanField,
)
from wtforms.fields.html5 import EmailField
from wtforms.validators import DataRequired
from wtforms.validators import StopValidation
from .base import BaseForm
from ..models.mpower import User
from ..auth import login


class ConfirmForm(BaseForm):
    confirm = BooleanField()


class LoginConfirmForm(BaseForm):
    email = EmailField(description='Correo electrónico', validators=[DataRequired()])
    password = PasswordField(description='Contraseña', validators=[DataRequired()])

    def validate_password(self, field):
        email = self.email.data.lower()
        user = User.query.filter_by(email=email).first()
        if not user or not user.check_password(field.data):
            raise StopValidation('El correo electrónico que ingresaste no coinciden con ninguna cuenta.')

        if self.confirm.data:
            login(user, False)
