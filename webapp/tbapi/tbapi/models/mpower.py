from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.orm import relationship
from sqlalchemy import inspect
from authlib.flask.oauth2.sqla import OAuth2ClientMixin
from authlib.flask.oauth2.sqla import OAuth2TokenMixin

import json

from .base import db, Base, SerializeMixin

class Patient(SerializeMixin, db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['patients']



# `users`.`username`,
# `users`.`password`,
# `users`.`first_name`,
# `users`.`last_name`,
# `users`.`email`,
# `users`.`change_pw_flag`,
# `users`.`clinic_id`,
# `users`.`language`,
# `users`.`last4ssn`,
# `users`.`registered`

class User(SerializeMixin, db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['users']
    
    def get_user_id(self):
        return self.id

    @property
    def password(self):
        return self._password

    @password.setter
    def password(self, raw):
        self._password = generate_password_hash(raw)

    def check_password(self, raw):
        if not self._password:
            return False
        return check_password_hash(self._password, raw)

    @classmethod
    def get_or_create(cls, profile):
        user = cls.query.filter_by(email=profile.email).first()
        if user:
            return user
        user = cls(email=profile.email, name=profile.name)
        user._password = '!'
        with db.auto_commit():
            db.session.add(user)
        return user

    def to_dict(self):
        return dict(id=self.id, name=self.name)    