import React from 'react'
import { useState, useEffect } from 'react'
import CheckBoxes from './components/CheckBoxes'
import ContactCard from './components/ContactCard'
import SearchBar from './components/SearchBar'
import contactService from './services/contacts'

const App = () => {
  const [searchQuery, setSearchQuery] = useState('')
  const [checkedProfessions, setCheckedProfessions] = useState([])
  const [contacts, setContacts] = useState(null)

  useEffect(() => {
    contactService
      .getAll()
      .then((result) => setContacts(result))
      .catch((error) => console.error(error))
  }, [])

  const handleSearch = () => {
    if (!searchQuery) alert('Fill in the search query')
    else
      contactService
        .search(searchQuery, checkedProfessions)
        .then((results) => {
          setContacts(results)
        })
        .catch((error) => {
          console.error(error)
        })
  }

  return (
    <div className="max-w-[80%] my-0 mx-auto">
      <SearchBar
        searchQuery={searchQuery}
        setSearchQuery={setSearchQuery}
        handleSearch={handleSearch}
      />
      <CheckBoxes setCheckedProfessions={setCheckedProfessions} />
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {contacts &&
          (contacts.length === 0 ? (
            <span className="text-white text-2xl my-8">
              No result found, try with other parameters
            </span>
          ) : (
            contacts.map((contact) => <ContactCard contact={contact} key={contact._id} />)
          ))}
      </div>
    </div>
  )
}

export default App
