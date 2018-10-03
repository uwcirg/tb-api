from sqlalchemy import Column
from sqlalchemy import (
    Integer, String, Text
)
from sqlalchemy.orm import Session

import json
from werkzeug.security import generate_password_hash, check_password_hash

from .base import db, SerializeMixin

class Note(db.Model, SerializeMixin):
    __bind_key__ = 'tbapi'
    __tablename__ = 'tbapi_notes'
    
    @classmethod
    def get_by_patient_id(cls, patient_id):
        notes = cls.query.filter_by(patient_id=patient_id).all()
        return notes

    @classmethod
    def get_by_note(cls, id):
        notes = cls.query.filter_by(id=id).all()
        return notes

    @classmethod
    def post(cls, noteObj):
        
        notes = cls.query.filter_by(
            patient_id=noteObj['patient_id'], 
            text=noteObj['text'], 
            created=noteObj['created'], 
            author_id=noteObj['author_id'], 
            lastmod=noteObj['lastmod']).all()

        if notes: # exists
            return notes[0].id    
        
        note= cls( #create instance
            patient_id=noteObj['patient_id'], 
            text=noteObj['text'], 
            created=noteObj['created'], 
            author_id=noteObj['author_id'], 
            lastmod=noteObj['lastmod'])

        try:
            db.session.add(note)
            db.session.commit()
        except:
            db.session.rollback()
            raise

        notes = cls.query.filter_by(
            patient_id=noteObj['patient_id'], 
            text=noteObj['text'], 
            created=noteObj['created'], 
            author_id=noteObj['author_id'], 
            lastmod=noteObj['lastmod']).all()
        
        return notes[0].id