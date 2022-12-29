<?php
/*+------------------------------------+
* | Session expiration time in minutes |
* +------------------------------------+*/
const SESSION_MINUTES = 60;
/*+------------------------+
* | Key to Json Web Tokens |
* +------------------------+*/
const API_JWT_SECRET = 'pr1v4t3%@cc3ss%t0k3n%futb0lcl0ud';

/*+-----------------------------+
* | Key to App names encryption |
* +-----------------------------+*/
const ENCRYPTION_KEY = 'pr1vAt3&3ncrYptkEy&fUtboLCl0ud';

/*+-----------------------------------------+
* | Requests accepted without authorization |
* +-----------------------------------------+*/
const AUTHORIZE_REQUESTS = ['/login', '/signup', '/logo', '/verifySession'];

/*+-----------------+
* | Authorized APPs |
* +-----------------+*/
const APPS = array(
    'ZIZU_FC',
    'BARCA'
);

/*+-----------------------+
* | User roles definition |
* +-----------------------+*/
const ROLES = array(
    'ADMIN' => 'Administrador',
    'TRAINER' => 'Formador',
    'FAMILY' => 'Familiar',
    'COORDINATOR' => 'Coordinador'
);