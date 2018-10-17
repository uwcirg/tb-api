"""create_notes_table

Revision ID: 27935569a4ca
Revises: a0a902e9fba9
Create Date: 2018-09-21 20:15:08.405530

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '27935569a4ca'
down_revision = 'a0a902e9fba9'
branch_labels = None
depends_on = None


def upgrade(engine_name):
    globals()["upgrade_%s" % engine_name]()


def downgrade(engine_name):
    globals()["downgrade_%s" % engine_name]()


def upgrade_():
    op.create_table('notes',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('patient_id', sa.Integer(), nullable=False),
    sa.Column('text', sa.String(length=10000), nullable=True),
    sa.Column('title', sa.Integer(), nullable=False),
    sa.Column('created', sa.DATETIME(), nullable=False),
    sa.Column('lastmod', sa.DATETIME(), nullable=False),
    sa.PrimaryKeyConstraint('id'),
    sa.UniqueConstraint('id'),
    )
    op.create_index(op.f('ix_notes_patient_id'), 'notes', ['patient_id'], unique=False)
    op.create_index(op.f('ix_notes_title'), 'notes', ['title'], unique=False)
    

def downgrade_():
   op.drop_index(op.f('ix_notes_patient_id'), table_name='notes')
   op.drop_index(op.f('ix_notes_title'), table_name='notes')
   op.drop_table('notes')

def upgrade_mpower():
	pass

def downgrade_mpower():
	pass

