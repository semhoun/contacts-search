import React from 'react'
import { useState } from 'react'
import { professions } from '../utils/professions'

const CheckBoxes = ({ setCheckedProfessions }) => {
  const [checkedState, setCheckedState] = useState(
    new Array(professions.length).fill(false)
  )

  const handleOnChange = (position) => {
    const updatedCheckedState = checkedState.map((item, index) =>
      index === position ? !item : item
    )

    setCheckedState(updatedCheckedState)

    const checked = []

    updatedCheckedState.map((currentVal, index) => {
      if (currentVal) {
        return checked.push(professions[index])
      }
      return null
    })

    setCheckedProfessions(checked)
  }

  return (
    <ul className="grid grid-cols-auto-fill gap-4 my-3 text-white">
      {professions.map((profession, index) => {
        return (
          <li key={index}>
            <div className="toppings-list-item">
              <div className="left-section">
                <input
                  type="checkbox"
                  id={`custom-checkbox-${index}`}
                  name={profession}
                  value={profession}
                  checked={checkedState[index]}
                  onChange={() => handleOnChange(index)}
                  required
                />
                <label htmlFor={`custom-checkbox-${index}`}>{profession}</label>
              </div>
            </div>
          </li>
        )
      })}
    </ul>
  )
}

export default CheckBoxes
