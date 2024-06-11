



import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;
import java.sql.*;

public class LogInServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    public void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        response.setContentType("text/html");
        PrintWriter out = response.getWriter();

        // Retrieve username and password from HTML form
        String usernameEntered = request.getParameter("username");
        String passwordEntered = request.getParameter("password");

        // JDBC URL, username, and password of MySQL server
        String jdbcUrl = "jdbc:mysql://localhost:3306/postgres";
        String dbUsername = "postgres";
        String dbPassword = "123";

        try {
            // Load MySQL JDBC driver
            Class.forName("com.mysql.jdbc.Driver");

            // Connect to the MySQL database
            Connection connection = DriverManager.getConnection(jdbcUrl, dbUsername, dbPassword);

            // Query to retrieve username and password from My_Salon table
            String sql = "SELECT Username, Passwords FROM My_Salon";
            PreparedStatement statement = connection.prepareStatement(sql);

            // Execute the query
            ResultSet resultSet = statement.executeQuery();

            // Check if username and password match
            while (resultSet.next()) {
                String usernameFromDB = resultSet.getString("Username");
                String passwordFromDB = resultSet.getString("Passwords");
                if (usernameFromDB.equals(usernameEntered) && passwordFromDB.equals(passwordEntered)) {
                    // Username and password match, forward to a success page
                    RequestDispatcher rd = request.getRequestDispatcher("Admin.jsp");
                    rd.forward(request, response);
                    return; // Exit the method after forwarding
                }
            }
            
            // If no matching username and password found, forward to a failure page
            RequestDispatcher rd = request.getRequestDispatcher("error.html");
            rd.forward(request, response);

        } catch (ClassNotFoundException | SQLException e) {
            // Handle any errors
            e.printStackTrace();
            // Forward to an error page
            RequestDispatcher rd = request.getRequestDispatcher("error.html");
            rd.forward(request, response);
        }
    }
}
