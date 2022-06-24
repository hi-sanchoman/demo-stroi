import { ref } from 'vue'
import axios from "axios";
import { useRouter } from 'vue-router';

export default function useApplications() {
    const applications = ref([])
    const application = ref([])
    const router = useRouter()
    const errors = ref('')

    const getApplications = async () => {
        let response = await axios.get('/api/v1/applications')
        applications.value = response.data.data;
    }

    const getApplication = async (id) => {
        let response = await axios.get('/api/v1/applications/' + id)
        application.value = response.data.data;
    }

    const storeApplication = async (data) => {
        console.log('store application...', data)
        return

        errors.value = ''

        try {
            await axios.post('/api/v1/applications', data)
            await router.push({name: 'applications.index'})
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const updateApplication = async (id) => {
        errors.value = ''

        try {
            await axios.put('/api/v1/applications/' + id, application.value)
            await router.push({name: 'applications.index'})
        } catch (e) {
            if (e.response.status === 422) {
               errors.value = e.response.data.errors
            }
        }
    }

    const destroyApplication = async (id) => {
        await axios.delete('/api/v1/applications/' + id)
    }


    return {
        applications,
        application,
        errors,
        getApplications,
        getApplication,
        storeApplication,
        updateApplication,
        destroyApplication
    }
}