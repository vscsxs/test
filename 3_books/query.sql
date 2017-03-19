SELECT
  books.*,
  COUNT(authors_has_books.author_id) as num
FROM books
  LEFT JOIN authors_has_books ON books.id = authors_has_books.book_id
GROUP BY books.id HAVING num > 2
