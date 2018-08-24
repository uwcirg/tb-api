from setuptools import setup, find_packages

setup(
    name='tbapi',
    packages=find_packages(),
    include_package_data=True,
    install_requires=[
        'Flask',
        'Flask-SQLAlchemy',
        'Flask-WTF',
        'gevent',
        'PyMySQL',
        'Flask-Migrate',
        'flask-cors',
        'Authlib[crypto]==0.5.1',
        'gunicorn',
        'json-logging-py'
    ],
    setup_requires=[
        'pytest-runner',
    ],
    tests_require=[
        'pytest',
    ],
)
