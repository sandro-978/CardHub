INSERT INTO users (username, email, password_hash) VALUES
('demo_user', 'demo@cardhub.local', 'demo_hash'),
('demo_user2', 'demo2@cardhub.local', 'demo_hash2');

INSERT INTO cards (name, game, edition, language, image_url) VALUES
('Drago Cremisi', 'Fantasy Cards', 'Prima Edizione', 'Italiano', '/assets/img/placeholder-card.png'),
('Mago delle Rune', 'Fantasy Cards', 'Set Base', 'Inglese', '/assets/img/placeholder-card.png'),
('Cavaliere Antico', 'Battle Deck', 'Promo', 'Italiano', '/assets/img/placeholder-card.png');

INSERT INTO listings (user_id, card_id, price, condition, description) VALUES
(1, 1, 49.90, 'Near Mint', 'Carta in condizioni eccellenti.'),
(1, 2, 18.50, 'Excellent', 'Leggeri segni di utilizzo.'),
(1, 3, 9.99, 'Good', 'Carta giocata ma integra.');

INSERT INTO chats (id_annuncio, id_acquirente, id_venditore) VALUES
(1,2,1);

INSERT INTO messaggi(id_chat,user_id,testo) VALUES
(1,2, 'Ciao, la carta è ancora disponibile?');