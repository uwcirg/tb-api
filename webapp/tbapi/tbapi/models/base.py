# coding: utf-8

# from contextlib import contextmanager
# from flask import g, current_app
from flask_sqlalchemy import SQLAlchemy 
from sqlalchemy import inspect

# class SQLAlchemy(_SQLAlchemy):
#     @contextmanager
#     def auto_commit(self, throw=True):
#         try:
#             yield
#             self.session.commit()
#         except Exception as e:
#             self.session.rollback()
#             if throw:
#                 raise e


db = SQLAlchemy()


# class Base(db.Model):
#     __abstract__ = True
#     metadata = MetaData()    

# class TBBase(db.Model):
#     __abstract__ = True
#     metadata = MetaData()


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
