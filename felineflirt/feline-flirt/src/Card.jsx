function Card({ name, age, image, onLike, onDislike }) {
    return (
        <div className="card">
            <img src={image} alt={`${name}`} />
            <h2>{name}</h2>
            <p>{age} years old</p>
            <button onClick={onLike}>Like</button>
            <button onClick={onDislike}>Dislike</button>
        </div>
    );
}

export default Card