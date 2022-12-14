<?php

/*+------------------------+
* | Key to Json Web Tokens |
* +------------------------+*/
const API_JWT_SECRET = 'pr1v4t3%@cc3ss%t0k3n%futb0lcl0ud';

/*+-----------------------------------------+
* | Requests accepted without authorization |
* +-----------------------------------------+*/
const AUTHORIZE_REQUESTS = ['/login', '/signup'];

/*+-----------------------+
* | User roles definition |
* +-----------------------+*/
const ROLES = array(
    'ADMIN' => 'Administrador',
    'TRAINER' => 'Formador',
    'FAMILY' => 'Familiar',
    'COORDINATOR' => 'Coordinador'
);