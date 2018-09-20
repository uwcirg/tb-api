from sqlalchemy import Column
from sqlalchemy import (
    Integer, String, Text
)
from sqlalchemy.orm import Session

import json
from werkzeug.security import generate_password_hash, check_password_hash

from .base import db, SerializeMixin



class Note(SerializeMixin, db.Model):
    __bind_key__ = 'mpower'
    __table__ = db.Model.metadata.tables['notes']

    @classmethod
    def get_by_patient_id(cls, patient_id):
        notes = cls.query.filter_by(patient_id=patient_id).all()
        if notes:
            return notes
        else: #empty
            return notes   

    @classmethod
    def get_by_note(cls, noteList):
        jsonObj = json.loads(noteList)
        pid = str(jsonObj['patient_id'])
        text = str(jsonObj['text'])
        print("pid=" + pid + "text=" + text)
        notes = cls.query.filter_by(patient_id=pid, text=text).all()
        if notes:
            return notes
        else: #empty
            return notes

    @classmethod
    def put(cls, noteList):
        jsonObj = json.loads(noteList)
        patient_id = str(jsonObj['patient_id'])
        text = str(jsonObj['text'])
        created = str(jsonObj['created'])
        author_id = str(jsonObj['author_id'])
        lastmod = str(jsonObj['lastmod'])
        flag_type = str(jsonObj['flag_type'])
        print("patient_id=" + patient_id + " text=" + text + " created=" + created)
        notes = cls.query.filter_by(patient_id=patient_id, text=text).all()
        if notes:
            return notes            
        notes = cls(patient_id=patient_id, text=text, author_id=author_id, lastmod=lastmod, flag_type=flag_type, created=created)
        try:
            db.session.add(notes)
            db.session.commit()
        except:
            db.session.rollback()
            raise
        return cls.query.filter_by(patient_id=patient_id, text=text, author_id=author_id, lastmod=lastmod, flag_type=flag_type, created=created).all()     

    @classmethod
    def post(cls, noteList):
        return Note.put(noteList)

    

# db.session.add(notes) delete close session.query(Widget).update({"q": 18}) execute
# id	int(11)	NO	PRI	
# patient_id	int(11)	NO	MUL	
# text	varchar(10000)	NO		
# author_id	int(11)	NO	MUL	
# created	datetime	NO		
# lastmod	datetime	NO		
# flagged	tinyint(1)	NO		0
# flag_type	enum('Identifiers in note','Participant distress','Participant feedback','Provider feedback','Technical issue','Data integrity','Report to IRB')	YES		

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
    
    