import { ref } from 'vue'
import axios from "axios";
import { useRouter } from 'vue-router';

export default function useConstructions() {
    const constructions = ref([])
    // const construction = ref([])
    const router = useRouter()
    const errors = ref('')

    const getConstructions = async () => {
        let response = await axios.get('/api/v1/constructions')
        constructions.value = response.data.data;

        // console.log('constructions', response.data.data)
    }

    


    return {
        constructions,
        // construction,
        errors,
        getConstructions,
    }
}