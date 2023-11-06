/* eslint-disable import/no-anonymous-default-export */
import axios from 'axios'

const getAll = () => {
  const request = axios.get('/api/all')
  return request.then((response) => response.data)
}

const search = (text, professions) => {
  const params = {
    text,
    professions,
  }

  const request = axios.get('/api/search', { params })
  return request.then((response) => response.data)
}

export default { getAll, search }
