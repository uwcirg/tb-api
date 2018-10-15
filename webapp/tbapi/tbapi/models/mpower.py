from sqlalchemy import Column
from sqlalchemy import (
    Integer, String, Text
)
import json
from werkzeug.security import generate_password_hash, check_password_hash

from .base import db, SerializeMixin

class Patient(SerializeMixin, db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['patients']

# # `users`.`username`,
# # `users`.`password`,
# # `users`.`first_name`,
# # `users`.`last_name`,
# # `users`.`email`,
# # `users`.`change_pw_flag`,
# # `users`.`clinic_id`,
# # `users`.`language`,
# # `users`.`last4ssn`,
# # `users`.`registered`

class User(SerializeMixin, db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['users']
    
    def get_user_id(self):
        return self.id

    @property
    def pw(self):
        return self._password

    @pw.setter
    def pw(self, raw):
        self.password = generate_password_hash(raw)

    def check_password(self, raw):
        if not self.password:
            return False
        return check_password_hash(self.password, raw)

    @classmethod
    def get_or_create(cls, profile):
        user = cls.query.filter_by(email=profile.email).first()
        if user:
            return user
        user = cls(email=profile.email, username=profile.name)
        user.password = '!'
        with db.auto_commit():
            db.session.add(user)
        return user

    def to_dict(self):
        return dict(id=self.id, name=self.name)    


class UserAclLeaf(db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['user_acl_leafs']

class IdentityProvider(db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['identity_providers']
    
    