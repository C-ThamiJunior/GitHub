import { useEffect, useState } from 'react'
import './App.css'
import 'bootstrap/dist/css/bootstrap.min.css';

function App() {
    const [cats, setCats] = useState([]);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchCats = async () => {
            try {
                const response = await fetch('https://api.thecatapi.com/v1/images/search?limit=10');
                const data = await response.json();
                const formattedData = data.map(cat => ({
                    name: `Cat ${cat.id}`,
                    age: Math.floor(Math.random() * 10) + 1, // Random age for demo
                    image: cat.url,
                }));
                setCats(formattedData);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchCats();
    }, []);

    const handleLike = () => {
        setCurrentIndex((prevIndex) => prevIndex + 1);
    };

    const handleDislike = () => {
        setCurrentIndex((prevIndex) => prevIndex + 1);
    };

    if (loading) {
        return <h1 className="text-center">Loading...</h1>;
    }

    if (error) {
        return <h1 className="text-center">Error: {error}</h1>;
    }

    if (currentIndex >= cats.length) {
        return <h1 className="text-center">No more cats!</h1>;
    }

    const currentCat = cats[currentIndex];

    return (
        <div className="container d-flex justify-content-center align-items-center" style={{ height: '100vh' }}>
            <div className="card" style={{ width: '18rem' }}>
                <img src={currentCat.image} className="card-img-top" alt={currentCat.name} />
                <div className="card-body">
                    <h5 className="card-title">{currentCat.name}</h5>
                    <p className="card-text">{currentCat.age} years old</p>
                    <div className="d-flex justify-content-between">
                        <button className="btn btn-success" onClick={handleLike}>Like</button>
                        <button className="btn btn-danger" onClick={handleDislike}>Dislike</button>
                    </div>
                </div>
            </div>
        </div>
    );
}


export default App
