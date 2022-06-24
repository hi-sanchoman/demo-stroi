import axios from 'axios';

const baseUrl = 'http://localhost:8000/api/v1/'; //local-test
const ApiPath = `${baseUrl}/`;

const httpClient = axios.create({
    baseURL: ApiPath,
    headers: {
        Authorization: 'Bearer {token}',
        //timeout: 1000, // indicates, 1000ms ie. 1 second
        "Content-Type": "application/json",
    }
})

const authInterceptor = (config) => {
    config.headers['Authorization'] = 'Bearer ...';
    return config;
}

const errorInterceptor = error => {
    // check if it's a server error
    if (!error.response) {
        //notify.warn('Network/Server error');
        console.error('**Network/Server error');
        console.log(error.response);
        return Promise.reject(error);
    }

    // all the other error responses
    switch (error.response.status) {
        case 400:
            console.error(error.response.status, error.message);
            //notify.warn('Nothing to display', 'Data Not Found');
            break;

        case 401: // authentication error, logout the user
            //notify.warn('Please login again', 'Session Expired');
            console.error(error.response.status, error.message);
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            router.push('/login');
            break;

        default:
            console.error(error.response.status, error.message);
            //notify.error('Server Error');
    }

    return Promise.reject(error);
}


httpClient.interceptors.request.use(authInterceptor);
httpClient.interceptors.response.use(responseInterceptor, errorInterceptor);

export default httpClient;