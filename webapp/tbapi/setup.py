from setuptools import setup, find_packages

setup(
    name='tbapi',
    packages=find_packages(),
    include_package_data=True,
    install_requires=[
        'Flask==0.12.2',
        'Flask-SQLAlchemy==2.3.2',
        'Flask-WTF==0.14.2',
        'gevent',
        'PyMySQL',
        'Flask-Migrate',
        'flask-cors',
        'Authlib[crypto]==0.5.1'
    ],
    setup_requires=[
        'pytest-runner',
    ],
    tests_require=[
        'pytest',
    ],
)
