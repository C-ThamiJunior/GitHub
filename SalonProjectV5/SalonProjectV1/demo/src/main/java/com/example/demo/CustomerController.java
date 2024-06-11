package com.example.demo;

import java.util.List;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;

@Controller
public class CustomerController {

    @Autowired
    private CustomerRepository customerRepository;

    @Autowired
    private UserCredentialsRepository userCredentialsRepository;
    
    @Autowired
    private ReviewRepository reviewRepository;

    @GetMapping("/customers")
    public List<Customer> getAllCustomers() {
        return customerRepository.findAll();
    }

    @GetMapping("/home")
    public String home(Model model) {
        List<Customer> customers = customerRepository.findAll();
        model.addAttribute("customers", customers);
        return "home";
    }

    @GetMapping("/addCustomerForm")
    public String showAddCustomerForm(Model model) {
        model.addAttribute("customer", new Customer());
        return "add_customer";
    }

    @PostMapping("/addCustomer")
    public String addCustomer(@ModelAttribute("customer") Customer customer,Model model) {
        customerRepository.save(customer);
        List<Customer> customers = customerRepository.findAll();

        
        model.addAttribute("customers", customers);
        model.addAttribute("customer", customer);
        return "redirect:/appointment-success";
    }

    @GetMapping("/login")
    public String showLoginForm() {
        return "login";
    }

    @PostMapping("/login")
    public String login(@RequestParam String username, @RequestParam String password) {
        UserCredentials user = userCredentialsRepository.findByUsernameAndPassword(username, password);
        if (user != null) {
            return "redirect:/outcome?success=true"; // Redirect to the outcome page if login is successful
        } else {
            return "redirect:/login?error=true"; // Redirect back to login page with an error message
        }
    }
    @GetMapping("/outcome")
    public String showOutcome(@RequestParam(value = "success", required = false) String success, Model model) {
        List<Customer> customers = customerRepository.findAll();
        List<Review> reviews = reviewRepository.findAll();
        model.addAttribute("customers", customers);
        model.addAttribute("reviews", reviews);
        if (success != null) {
            model.addAttribute("success", true);
        }
        return "outcome";
    }



    @GetMapping("/addReviewForm")
    public String showAddReviewForm(Model model) {
        model.addAttribute("review", new Review());
        return "add_review";
    }

    @PostMapping("/addReview")
    public String addReview(@ModelAttribute("review") Review review, Model model) {
        reviewRepository.save(review);
        model.addAttribute("name", review.getName());
        return "thank_you";
    }
}
