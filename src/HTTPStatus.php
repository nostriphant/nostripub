<?php

namespace nostriphant\nostripub;

enum HTTPStatus: string {
    case _200 = 'OK';
    
    case _302 = 'Found';
    
    case _400 = 'Bad Request';
    case _404 = 'Not Found';
    case _422 = 'Unprocessable Content';
    
    case _500 = 'Internal Server Error';
}
