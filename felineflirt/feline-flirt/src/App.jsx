import { useEffect, useState } from 'react'
import { useSwipeable } from 'react-swipeable';
import { useSpring, animated } from '@react-spring/web';
import 'bootstrap/dist/css/bootstrap.min.css';
import './App.css';

const api_key = "DEMO_API_KEY";
const imagesUrl = `https://api.thecatapi.com/v1/images/search?limit=10`;
const namesUrl = `https://tools.estevecastells.com/api/cats/v1?limit=10`;

function App() {
    const [cats, setCats] = useState([]);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [animationProps, setAnimationProps] = useSpring(() => ({
        opacity: 1,
        transform: 'translate3d(0,0,0)',
    }));

    const fetchCatImages = async () => {
        try {
            const response = await fetch(imagesUrl, {
                headers: {
                    'x-api-key': api_key,
                },
            });
            const data = await response.json();
            return data.map(catImage => ({
                image: catImage.url,
                temperament: catImage.breeds?.[0]?.temperament || 'Unknown', // Assuming temperament info is in catImage
            }));
        } catch (err) {
            throw new Error('Error fetching cat images');
        }
    };

    const fetchCatNames = async () => {
        try {
            const response = await fetch(namesUrl);
            const data = await response.json();
            return data.map(cat => ({
                name: cat,
            }));
        } catch (err) {
            throw new Error('Error fetching cat names');
        }
    };

    const fetchCats = async () => {
        try {
            const [catImages, catNames] = await Promise.all([
                fetchCatImages(),
                fetchCatNames()
            ]);

            const formattedData = catImages.map((catImage, index) => ({
                ...catImage,
                name: catNames[index % catNames.length].name, // Rotate through names
                age: Math.floor(Math.random() * 10) + 1, // Random age for demo
            }));

            setCats(prevCats => [...prevCats, ...formattedData]);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCats();
    }, []);

    const handleLike = () => {
        setAnimationProps({
            opacity: 0,
            transform: 'translate3d(100%,0,0)',
            onRest: () => {
                setCurrentIndex((prevIndex) => prevIndex + 1);
                setAnimationProps({
                    opacity: 1,
                    transform: 'translate3d(0,0,0)',
                });
                if (currentIndex >= cats.length - 1) {
                    fetchCats();
                }
            },
        });
    };

    const handleDislike = () => {
        setAnimationProps({
            opacity: 0,
            transform: 'translate3d(-100%,0,0)',
            onRest: () => {
                setCurrentIndex((prevIndex) => prevIndex + 1);
                setAnimationProps({
                    opacity: 1,
                    transform: 'translate3d(0,0,0)',
                });
                if (currentIndex >= cats.length - 1) {
                    fetchCats();
                }
            },
        });
    };

    const handlers = useSwipeable({
        onSwipedLeft: handleDislike,
        onSwipedRight: handleLike,
        preventDefaultTouchmoveEvent: true,
        trackMouse: true,
    });

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
        <div {...handlers} className="container d-flex justify-content-center align-items-center" style={{ height: '100vh' }}>
            <animated.div style={animationProps} className="card" >
                <img src={currentCat.image} className="card-img-top" alt={currentCat.name} />
                <div className="card-body">
                    <h5 className="card-title">{currentCat.name}</h5>
                    <p className="card-text">{currentCat.age} years old</p>
                    <p className="card-text">Temperament: {currentCat.temperament}</p>
                    <div className="d-flex justify-content-between">
                        <button className="btn btn-success" onClick={handleLike}>Like</button>
                        <button className="btn btn-danger" onClick={handleDislike}>Dislike</button>
                    </div>
                </div>
            </animated.div>
        </div>
    );
}




export default App;
