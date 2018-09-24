from sqlalchemy import Column
from sqlalchemy import (
    Integer, String, Text
)
from sqlalchemy.orm import Session

import json
from werkzeug.security import generate_password_hash, check_password_hash

from .base import db, SerializeMixin

class Note(SerializeMixin, db.Model):
    __tablename__ = 'notes'

    @classmethod
    def get_by_patient_id(cls, patient_id):
        notes = cls.query.filter_by(patient_id=patient_id).all()
        if notes:
            return notes
        else: #empty
            return notes   

    @classmethod
    def get_by_note(cls, noteList):
        notes = Note.filter_by_fields(noteList)
        if notes:
            return notes
        else: #empty
            return notes

    @classmethod
    def put(cls, noteList):
        notes = Note.filter_by_fields(noteList)
        if notes:
            return notes    
        noteDict = Note.load_fields(noteList)        
        notes = cls(
            patient_id=noteDict['patient_id'], 
            text=noteDict['text'], 
            created=noteDict['created'], 
            author_id=noteDict['author_id'], 
            lastmod=noteDict['lastmod'], 
            flag_type=noteDict['flag_type'])
        try:
            db.session.add(notes)
            db.session.commit()
        except:
            db.session.rollback()
            raise
        return Note.filter_by_fields(noteList)

    @classmethod
    def post(cls, noteList):
        return Note.put(noteList)

    @classmethod
    def filter_by_fields(cls, noteList):
        noteDict = Note.load_fields(noteList)
        return cls.query.filter_by(
            patient_id=noteDict['patient_id'], 
            text=noteDict['text'], 
            created=noteDict['created'], 
            author_id=noteDict['author_id'], 
            lastmod=noteDict['lastmod'], 
            flag_type=noteDict['flag_type']).all()     


    @classmethod
    def load_fields(cls, noteList):
        jsonObj = json.loads(noteList)
        noteDict = {
        'patient_id': str(jsonObj['patient_id']),
        'text': str(jsonObj['text']),
        'created': str(jsonObj['created']),
        'author_id': str(jsonObj['author_id']),
        'lastmod': str(jsonObj['lastmod']),
        'flag_type': str(jsonObj['flag_type']),
        }
        return noteDict
