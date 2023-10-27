const express = require('express')
const axios = require('axios')
const client = require('../elasticsearch/client')

const movies = require('./movies.json')

const moviesRouter = express.Router()

moviesRouter.get('/', async (request, response) => {
  response.json('Retrieving in theaters movies ...')
  try {
    console.log('Processing ...')

    console.log('Data retrieved successfuly')

    const results = movies.items

    console.log('Starting data indexation ...')

    results.map(async (result) => {
      await client.index({
        index: 'movies',
        id: result.id,
        body: result,
        pipeline: 'movies_pipeline',
      })
    })

    console.log('Data has been indexed')
  } catch (error) {
    console.log(error)
  }
})

module.exports = moviesRouter
