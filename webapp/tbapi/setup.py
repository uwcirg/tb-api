from setuptools import setup, find_packages

setup(
    name='tbapi',
    packages=find_packages(),
    include_package_data=True,
    install_requires=[
        'flask',
        'PyMySQL',
        'flask-sqlalchemy',
        'Flask-Migrate',
        'flask-cors',
        'Flask-OAuthlib', 
        'flask-bcrypt'
    ],
    setup_requires=[
        'pytest-runner',
    ],
    tests_require=[
        'pytest',
    ],
)
