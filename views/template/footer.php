<?php
// views/template/footer.php
?>
            </div> <!-- /.col-md-10 -->
        </div> <!-- /.row -->
    </div> <!-- /.container-fluid -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        console.log('Dashboard loaded');
        console.log('Session username:', '<?= $_SESSION['username'] ?? 'none' ?>');
    </script>
</body>
</html>