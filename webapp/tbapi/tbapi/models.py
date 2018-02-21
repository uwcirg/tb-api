from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.orm import relationship
from sqlalchemy import inspect

import json

from .db import db

class SerializeMixin(object):
    # def __repr__(self):
    #     return json.dumps(self.serialize)

    @property
    def serialize(self):
       """Return object data in easily serializeable format"""

       serialized = {}

       for key in inspect(self.__class__).columns.keys():
           serialized[key] = getattr(self, key)

       return serialized


class Patient(SerializeMixin, db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['patients']
    #__tablename__ = 'patients'

class MpowerUser(SerializeMixin, db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['users']
    #__tablename__ = 'users'

    # patient = relationship('Patient', backref='user', lazy=True, primaryjoin="User.id == foreign(Patient.consenter_id)")
