import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'

function App() {
  const [count, setCount] = useState(0)

  return (
    <div className='w-1/2 h-80 mx-auto flex flex-col
    justify-center items-center'>
      <img className='animate-spin mb-8' src={reactLogo} />
      Привет, я маленький гараж.
      Я обязательно вырасту.
    </div>
  )
}

export default App
