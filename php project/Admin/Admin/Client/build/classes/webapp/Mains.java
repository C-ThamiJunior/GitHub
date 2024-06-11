

import org.eclipse.jetty.server.Server;
import org.eclipse.jetty.servlet.ServletContextHandler;
import org.eclipse.jetty.servlet.ServletHolder;

public class Mains {

    public static void main(String[] args) throws Exception {
        // Create a Jetty server on port 8080
        Server server = new Server(8080);

        // Create a servlet context handler
        ServletContextHandler handler = new ServletContextHandler(ServletContextHandler.SESSIONS);
        handler.setContextPath("/");

        // Add your servlet to the context handler
        handler.addServlet(new ServletHolder(new LogInServlet()), "/login");

        // Attach the servlet context handler to the server
        server.setHandler(handler);

        // Start the server
        server.start();
        System.out.println("Server started at http://localhost:8080");

        // Wait for the server to finish execution
        server.join();
    }
}
