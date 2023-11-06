import React from 'react'
import Gravatar from 'react-gravatar'

const ContactCard = ({ contact }) => {
  contact = contact._source
  return (
    <div className="w-1/2 md:w-1/2 lg:w-[470px] max-w-4xl rounded  shadow-lg m-4 flex justify-between">
      <div className="flex flex-col flex-grow px-8 py-4 bg-color-333">
		<div className="md:flex-shrink-0">
			<Gravatar email="{contact.email[0]}" />
		</div>
        <h3 className="font-bold text-4xl md:text-2xl lg:text-2xl text-gray-200 contact--title">
          {contact.name.first} {contact.name.last} {contact.email[0]}
        </h3>
        <span className="contact--genres my-2 text-xl lg:text-sm lg:mb-4">
          {contact.externalId}
        </span>
        <div className="flex-grow">
          <p className="text-xl md:text-base lg:text-base text-gray-100 leading-snug truncate-overflow hover:overflow-y-scroll">
            {contact.info}
          </p>
        </div>
        <div className="flex-grow">
          <p className="text-xl my-5 md:text-base lg:text-base text-gray-500 leading-snug">
            Country: {contact.address.country} <br/>
			City: {contact.address.city}
          </p>
        </div>
        <div className="button-container flex justify-between mb-2">
          <span className="text-lg mr-4 lg:text-sm font-bold text-orange-700">
            {contact.email[0]} <br/> {contact.email[1]}
          </span>
          <span className="text-lg mr-4 lg:text-sm font-bold text-green-500">
            {contact.profession}
          </span>
        </div>
      </div>
    </div>
  )
}

export default ContactCard
