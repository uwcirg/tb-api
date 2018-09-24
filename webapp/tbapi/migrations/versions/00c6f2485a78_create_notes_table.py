flask@0fb8e58ebc5c:~/app/web/tbapi/migrations/versions$ cat 00c6f2485a78_create_notes_table.py
"""create notes table

Revision ID: 00c6f2485a78
Revises: a0a902e9fba9
Create Date: 2018-09-21 20:15:08.405530

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '00c6f2485a78'
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
    sa.Column('author_id', sa.Integer(), nullable=False),
    sa.Column('created', mysql.DATETIME(), nullable=False),
    sa.Column('lastmod', mysql.DATETIME(), nullable=False),
    sa.Column('flagged', mysql.TINYINT(), nullable=False),
    sa.Column('flag_type', mysql.ENUM('Identifiers in note','Participant distress','Participant feedback','Provider feedback','Technical issue','Data integrity','Report to IRB'='utf8', collation='utf8_unicode_ci'), nullable=True), 
    sa.PrimaryKeyConstraint('id'),
    sa.UniqueConstraint('id')
    )
    op.create_index(op.f('ix_notes_patient_id'), 'notes', ['patient_id'], unique=False)
    op.create_index(op.f('ix_notes_author_id'), 'notes', ['author_id'], unique=False)
    mysql_default_charset='latin1',
    mysql_engine='InnoDB'

def downgrade_():
    pass


def upgrade_mpower():
    pass


def downgrade_mpower():
    pass

flask@0fb8e58ebc5c:~/app/web/tbapi/migrations/versions$

